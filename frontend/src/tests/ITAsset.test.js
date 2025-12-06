/**
 * IT Asset Register Frontend Tests
 * 
 * This file contains tests for the IT Asset Register React components.
 * Run with: npm test
 */

import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';
import ITAssetForm from '../components/ITAssetForm';
import ITAssetReport from '../components/ITAssetReport';
import API from '../services/api';

// Mock the API service
jest.mock('../services/api');
const mockAPI = API;

describe('IT Asset Register Components', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  describe('ITAssetForm', () => {
    const mockOnSuccess = jest.fn();
    const mockOnCancel = jest.fn();

    beforeEach(() => {
      mockOnSuccess.mockClear();
      mockOnCancel.mockClear();
    });

    test('renders form with all required fields', () => {
      render(<ITAssetForm onSuccess={mockOnSuccess} onCancel={mockOnCancel} />);
      
      expect(screen.getByLabelText(/asset description/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/building/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/floor/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/department/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/room/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/condition/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/status/i)).toBeInTheDocument();
    });

    test('shows create asset title for new asset', () => {
      render(<ITAssetForm onSuccess={mockOnSuccess} onCancel={mockOnCancel} />);
      
      expect(screen.getByText('Add New IT Asset')).toBeInTheDocument();
    });

    test('shows edit asset title for existing asset', () => {
      const mockAsset = {
        id: 1,
        asset_description: 'Test Asset',
        building: 'Test Building',
        floor: '1st Floor',
        department: 'Test Department',
        room: 'Room 101',
        condition: 'good',
        status: 'active',
        notes: 'Test notes'
      };

      mockAPI.get.mockResolvedValue({
        data: {
          success: true,
          data: mockAsset
        }
      });

      render(<ITAssetForm assetId={1} onSuccess={mockOnSuccess} onCancel={mockOnCancel} />);
      
      expect(screen.getByText('Edit IT Asset')).toBeInTheDocument();
    });

    test('handles form submission for new asset', async () => {
      const mockResponse = {
        data: {
          success: true,
          data: {
            id: 1,
            asset_description: 'Test Asset',
            building: 'Test Building',
            floor: '1st Floor',
            department: 'Test Department',
            room: 'Room 101',
            condition: 'good',
            status: 'active'
          }
        }
      };

      mockAPI.post.mockResolvedValue(mockResponse);

      render(<ITAssetForm onSuccess={mockOnSuccess} onCancel={mockOnCancel} />);
      
      // Fill in the form
      fireEvent.change(screen.getByLabelText(/asset description/i), {
        target: { value: 'Test Asset' }
      });
      fireEvent.change(screen.getByLabelText(/building/i), {
        target: { value: 'Test Building' }
      });
      fireEvent.change(screen.getByLabelText(/floor/i), {
        target: { value: '1st Floor' }
      });
      fireEvent.change(screen.getByLabelText(/department/i), {
        target: { value: 'Test Department' }
      });
      fireEvent.change(screen.getByLabelText(/room/i), {
        target: { value: 'Room 101' }
      });

      // Submit the form
      fireEvent.click(screen.getByText('Create Asset'));

      await waitFor(() => {
        expect(mockAPI.post).toHaveBeenCalledWith('/it-assets', expect.objectContaining({
          asset_description: 'Test Asset',
          building: 'Test Building',
          floor: '1st Floor',
          department: 'Test Department',
          room: 'Room 101',
          condition: 'good',
          status: 'active'
        }));
      });

      await waitFor(() => {
        expect(mockOnSuccess).toHaveBeenCalledWith(mockResponse.data.data);
      });
    });

    test('handles form validation errors', async () => {
      const mockErrorResponse = {
        response: {
          data: {
            errors: {
              asset_description: ['The asset description field is required.'],
              building: ['The building field is required.']
            }
          }
        }
      };

      mockAPI.post.mockRejectedValue(mockErrorResponse);

      render(<ITAssetForm onSuccess={mockOnSuccess} onCancel={mockOnCancel} />);
      
      // Submit empty form
      fireEvent.click(screen.getByText('Create Asset'));

      await waitFor(() => {
        expect(screen.getByText(/validation errors/i)).toBeInTheDocument();
      });
    });

    test('calls onCancel when cancel button is clicked', () => {
      render(<ITAssetForm onSuccess={mockOnSuccess} onCancel={mockOnCancel} />);
      
      fireEvent.click(screen.getByText('Clear Form'));
      
      expect(mockOnCancel).toHaveBeenCalled();
    });
  });

  describe('ITAssetReport', () => {
    const mockAssets = [
      {
        id: 1,
        asset_number: 'IT2025010001',
        asset_description: 'Test Laptop',
        building: 'Main Building',
        floor: '2nd Floor',
        department: 'IT Department',
        room: 'Room 201',
        condition: 'excellent',
        status: 'active',
        notes: 'Test asset'
      },
      {
        id: 2,
        asset_number: 'IT2025010002',
        asset_description: 'Test Printer',
        building: 'Annex Building',
        floor: '1st Floor',
        department: 'HR Department',
        room: 'Room 101',
        condition: 'good',
        status: 'maintenance',
        notes: 'Another test asset'
      }
    ];

    const mockStatistics = {
      total: 2,
      active: 1,
      maintenance: 1,
      disposed: 0,
      conditions: {
        excellent: 1,
        good: 1
      }
    };

    beforeEach(() => {
      mockAPI.get.mockImplementation((url) => {
        if (url.includes('/it-assets/statistics')) {
          return Promise.resolve({
            data: {
              success: true,
              data: mockStatistics
            }
          });
        }
        if (url.includes('/it-assets')) {
          return Promise.resolve({
            data: {
              success: true,
              data: mockAssets
            }
          });
        }
        return Promise.reject(new Error('Unknown endpoint'));
      });
    });

    test('renders asset report with statistics', async () => {
      render(<ITAssetReport />);
      
      expect(screen.getByText('IT Asset Register')).toBeInTheDocument();
      
      await waitFor(() => {
        expect(screen.getByText('2')).toBeInTheDocument(); // Total assets
        expect(screen.getByText('1')).toBeInTheDocument(); // Active assets
      });
    });

    test('renders asset table with data', async () => {
      render(<ITAssetReport />);
      
      await waitFor(() => {
        expect(screen.getByText('IT2025010001')).toBeInTheDocument();
        expect(screen.getByText('Test Laptop')).toBeInTheDocument();
        expect(screen.getByText('Main Building')).toBeInTheDocument();
        expect(screen.getByText('IT Department')).toBeInTheDocument();
      });
    });

    test('handles filter changes', async () => {
      render(<ITAssetReport />);
      
      await waitFor(() => {
        expect(screen.getByText('IT Asset Register')).toBeInTheDocument();
      });

      // Change status filter
      const statusFilter = screen.getByLabelText(/status/i);
      fireEvent.change(statusFilter, { target: { value: 'active' } });

      // Apply filters
      fireEvent.click(screen.getByText('Apply Filters'));

      await waitFor(() => {
        expect(mockAPI.get).toHaveBeenCalledWith(
          expect.stringContaining('status=active')
        );
      });
    });

    test('handles search functionality', async () => {
      render(<ITAssetReport />);
      
      await waitFor(() => {
        expect(screen.getByText('IT Asset Register')).toBeInTheDocument();
      });

      // Enter search term
      const searchInput = screen.getByPlaceholderText(/search by asset number/i);
      fireEvent.change(searchInput, { target: { value: 'laptop' } });

      // Apply filters
      fireEvent.click(screen.getByText('Apply Filters'));

      await waitFor(() => {
        expect(mockAPI.get).toHaveBeenCalledWith(
          expect.stringContaining('search=laptop')
        );
      });
    });

    test('handles CSV export', async () => {
      // Mock blob response for CSV export
      const mockBlob = new Blob(['csv,data'], { type: 'text/csv' });
      mockAPI.get.mockResolvedValueOnce({
        data: mockBlob
      });

      render(<ITAssetReport />);
      
      await waitFor(() => {
        expect(screen.getByText('IT Asset Register')).toBeInTheDocument();
      });

      // Click export button
      fireEvent.click(screen.getByText('Export CSV'));

      await waitFor(() => {
        expect(mockAPI.get).toHaveBeenCalledWith(
          expect.stringContaining('/it-assets/export/csv')
        );
      });
    });

    test('shows add new asset form when button is clicked', async () => {
      render(<ITAssetReport />);
      
      await waitFor(() => {
        expect(screen.getByText('IT Asset Register')).toBeInTheDocument();
      });

      // Click add new asset button
      fireEvent.click(screen.getByText('Add New Asset'));

      await waitFor(() => {
        expect(screen.getByText('Add New IT Asset')).toBeInTheDocument();
      });
    });

    test('handles asset deletion', async () => {
      // Mock window.confirm
      window.confirm = jest.fn(() => true);

      mockAPI.delete.mockResolvedValue({
        data: {
          success: true,
          message: 'Asset deleted successfully'
        }
      });

      render(<ITAssetReport />);
      
      await waitFor(() => {
        expect(screen.getByText('IT Asset Register')).toBeInTheDocument();
      });

      // Click delete button for first asset
      const deleteButtons = screen.getAllByText('Delete');
      fireEvent.click(deleteButtons[0]);

      await waitFor(() => {
        expect(window.confirm).toHaveBeenCalledWith('Are you sure you want to delete this asset?');
        expect(mockAPI.delete).toHaveBeenCalledWith('/it-assets/1');
      });
    });
  });
});

// Integration test for complete workflow
describe('IT Asset Register Integration', () => {
  test('complete asset management workflow', async () => {
    const mockAssets = [];
    const mockStatistics = {
      total: 0,
      active: 0,
      maintenance: 0,
      disposed: 0,
      conditions: {}
    };

    // Mock API responses
    mockAPI.get.mockImplementation((url) => {
      if (url.includes('/it-assets/statistics')) {
        return Promise.resolve({
          data: {
            success: true,
            data: mockStatistics
          }
        });
      }
      if (url.includes('/it-assets')) {
        return Promise.resolve({
          data: {
            success: true,
            data: mockAssets
          }
        });
      }
      return Promise.reject(new Error('Unknown endpoint'));
    });

    mockAPI.post.mockResolvedValue({
      data: {
        success: true,
        data: {
          id: 1,
          asset_number: 'IT2025010001',
          asset_description: 'New Test Asset',
          building: 'Test Building',
          floor: '1st Floor',
          department: 'Test Department',
          room: 'Room 101',
          condition: 'good',
          status: 'active'
        }
      }
    });

    render(<ITAssetReport />);
    
    // Wait for initial load
    await waitFor(() => {
      expect(screen.getByText('IT Asset Register')).toBeInTheDocument();
    });

    // Click add new asset
    fireEvent.click(screen.getByText('Add New Asset'));

    // Wait for form to appear
    await waitFor(() => {
      expect(screen.getByText('Add New IT Asset')).toBeInTheDocument();
    });

    // Fill and submit form
    fireEvent.change(screen.getByLabelText(/asset description/i), {
      target: { value: 'New Test Asset' }
    });
    fireEvent.change(screen.getByLabelText(/building/i), {
      target: { value: 'Test Building' }
    });
    fireEvent.change(screen.getByLabelText(/floor/i), {
      target: { value: '1st Floor' }
    });
    fireEvent.change(screen.getByLabelText(/department/i), {
      target: { value: 'Test Department' }
    });
    fireEvent.change(screen.getByLabelText(/room/i), {
      target: { value: 'Room 101' }
    });

    fireEvent.click(screen.getByText('Create Asset'));

    // Verify API call was made
    await waitFor(() => {
      expect(mockAPI.post).toHaveBeenCalledWith('/it-assets', expect.objectContaining({
        asset_description: 'New Test Asset',
        building: 'Test Building',
        floor: '1st Floor',
        department: 'Test Department',
        room: 'Room 101'
      }));
    });
  });
});
